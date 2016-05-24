import React, { PropTypes, Component } from 'react';
import { Link } from 'react-router';

class MeasuresPage extends Component {

  measuresList () {
    if(this.props.measures && this.props.measures.length) {
      return (
        <div>
          {measures.map((measure, index) => (
            <div key={index} className='measure'>
              <h4><Link to={"/dashboard/Measures/" + measure._id}>{measure.title}</Link></h4>
            </div>
          ))}
        </div>
      )
    }
    // No measures, return empty
    return this.props.emptyText;
  }

  render () {
    return (
      <div>
        {this.props.header}
        {this.measuresList()}
      </div>
    );
  }
}

MeasuresPage.propTypes = {
  header: PropTypes.object.isRequired,
  emptyText: PropTypes.object.isRequired,
  measures: PropTypes.array.isRequired
};

export default MeasuresPage;
